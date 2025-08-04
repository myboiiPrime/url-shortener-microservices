FROM mcr.microsoft.com/dotnet/aspnet:8.0 AS base
WORKDIR /app
EXPOSE 80
EXPOSE 443

FROM mcr.microsoft.com/dotnet/sdk:8.0 AS build
WORKDIR /src
COPY ["src/AnalyticsService/UrlShortener.AnalyticsService.csproj", "src/AnalyticsService/"]
COPY ["src/Shared/UrlShortener.Shared.csproj", "src/Shared/"]
RUN dotnet restore "src/AnalyticsService/UrlShortener.AnalyticsService.csproj"
COPY . .
WORKDIR "/src/src/AnalyticsService"
RUN dotnet build "UrlShortener.AnalyticsService.csproj" -c Release -o /app/build

FROM build AS publish
RUN dotnet publish "UrlShortener.AnalyticsService.csproj" -c Release -o /app/publish /p:UseAppHost=false

FROM base AS final
WORKDIR /app
COPY --from=publish /app/publish .
ENTRYPOINT ["dotnet", "UrlShortener.AnalyticsService.dll"]