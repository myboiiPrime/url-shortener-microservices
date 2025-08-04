FROM mcr.microsoft.com/dotnet/aspnet:8.0 AS base
WORKDIR /app
EXPOSE 80
EXPOSE 443

FROM mcr.microsoft.com/dotnet/sdk:8.0 AS build
WORKDIR /src
COPY ["src/UserService/UrlShortener.UserService.csproj", "src/UserService/"]
COPY ["src/Shared/UrlShortener.Shared.csproj", "src/Shared/"]
RUN dotnet restore "src/UserService/UrlShortener.UserService.csproj"
COPY . .
WORKDIR "/src/src/UserService"
RUN dotnet build "UrlShortener.UserService.csproj" -c Release -o /app/build

FROM build AS publish
RUN dotnet publish "UrlShortener.UserService.csproj" -c Release -o /app/publish /p:UseAppHost=false

FROM base AS final
WORKDIR /app
COPY --from=publish /app/publish .
ENTRYPOINT ["dotnet", "UrlShortener.UserService.dll"]